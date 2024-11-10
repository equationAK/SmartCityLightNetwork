'''
Author: Kyriakidis Aris
email: a.kyriakidis@hotmail.com
'''

import machine
import utime
import urequests
import network
import json
from machine import Pin, PWM


# Configure UART pins
tx_pin = machine.Pin(0)  # TX on GP0
rx_pin = machine.Pin(1)  # RX on GP1

# Configure UART
uart = machine.UART(0, baudrate=9600, tx=tx_pin, rx=rx_pin)

# Configure LED and LDR pins
led_Pin = Pin(18)          # Change to the appropriate GPIO pin
led_pwm = PWM(led_Pin)     # create a PWM object on that pin
led_pwm.freq(1000)

# Configure ADC for LDR
ldr_pin = 27  
ldr_threshold = 2000
ldr = machine.ADC(machine.Pin(ldr_pin))   # initialize an ADC object for pin 27

# Configure pin for PIR sensor
Pir_Pin = 22
pir = Pin(Pir_Pin, Pin.IN, Pin.PULL_DOWN)


# Configure Flame Sensor
flame_Sensor_1 = Pin(10, Pin.IN)
flame_Sensor_2 = Pin(11, Pin.IN)
flame_Sensor_3 = Pin(12, Pin.IN)


#variables

pole_name = 'picoXbee'
alarm_name = 'flame'



# Networking settings
ssid = ''
password = ''
THINGSPEAK_WRITE_API_KEY = ''

# Configure Pico W as Station
sta_if = network.WLAN(network.STA_IF)
sta_if.active(True)

if not sta_if.isconnected():
    print('Connecting to network...')
    sta_if.connect(ssid, password)
    while not sta_if.isconnected():
        pass
    print('Network config:', sta_if.ifconfig())



def receive_data():
    try:
        data_buffer = b""
        while uart.any() > 0:
            data_chunk = uart.read(512)  # Adjust the chunk size as needed
            if data_chunk:
                data_buffer += data_chunk
                if b'}' in data_buffer:
                    start_idx = data_buffer.find(b'{')
                    end_idx = data_buffer.find(b'}', start_idx)
                    if start_idx != -1 and end_idx != -1:
                        data = data_buffer[start_idx:end_idx + 2]
                        data_buffer = data_buffer[end_idx + 2:]
                        return data.strip()
        return None
    except Exception as e:
        print("UART Error:", e)
        return None

def rename_specific_keys(input_dict):
    try:
        #data = json.loads(input_json)
        
        # Access the nested ThingSpeak dictionary
        thingSpeak_data = input_dict.get("ThingSpeak", {})
        print(thingSpeak_data)
        
        # Rename specific keys within the ThingSpeak dictionary
        renamed_data = {
            "field1": thingSpeak_data.get("ldr", None),
            "field2": thingSpeak_data.get("temperature", None),
            "field3": thingSpeak_data.get("humidity", None),
            "field4": thingSpeak_data.get("mq7", None),
            "field5": thingSpeak_data.get("gasAlarm", None),
            "field6": thingSpeak_data.get("mq135", None)
        }
        
        return json.dumps(renamed_data)
    except json.JSONDecodeError as e:
        print("ThingSpeakJSON Decoding Error:", e)
        return None


def send_json_to_server(json_data):
    try:
        server_ip = "192.168.2.112"
        server_port = 3360

        # Specify the server URL with a concrete endpoint
        server_url = "http://{}:{}/".format(server_ip, server_port)  # Replace "api/data" with your actual endpoint

        # Set up HTTP headers
        headers = {'Content-Type': 'application/json'}

        # Convert JSON data to a string
        json_str = json.dumps(json_data)
        #debug
        print(json_str)

        # Make the HTTP GET request
        response = urequests.post(server_url, data=json_str, headers=headers)

        print("Request sent..")
        # Print the server's response (optional)
        print("Server Response:", response.text)

        # Close the request
        response.close()

    except Exception as e:
        print("Error:", e)


def check_Pir_status():
    pir_value = pir.value()
    print(pir_value)
    if pir_value == 1:
        print("Movement Detected")
    else:
        print("Waiting for movement")
    return pir_value


def manage_energy_saving():
    ldr_value = ldr.read_u16()
    print("LDR Value:", ldr_value)
    pir_status = check_Pir_status()
    
    # Check PIR status and adjust LED brightness accordingly
    if ldr_value > 5000:  # Bright day or full sunlight environment
        led_pwm.duty_u16(0)  # Keep LED OFF
    elif pir_status == 0:  # If motion is not detected
        if ldr_threshold < ldr_value <= 5000:  # Afternoon or cloudy without sunlight environment
            led_pwm.duty_u16(int(65535 * 0.3))  # Dim light to 30%
        elif ldr_value <= ldr_threshold:  # Darkness
            led_pwm.duty_u16(int(65535 * 0.4))  # Keep LED ON at 40% brightness
    else:  # Motion is detected
        led_pwm.duty_u16(65535)  # Turn on LED to 100% brightness
        print("Motion Detected - LED at 100% brightness")
    
    light_percentage = round(ldr_value / 65535 * 100, 2)
    return light_percentage


def flame_alarm():
    if flame_Sensor_1.value() == 1 or flame_Sensor_2.value() == 1 or flame_Sensor_3.value() == 1:
        print("Flame detected")
        # Sending alarm data example (replace "sensorName" with the actual sensor name)
        send_alarm_data_to_server(pole_name, alarm_name,1)
        utime.sleep(1)
    else:
        print("No flame")
        send_alarm_data_to_server(pole_name, alarm_name,0)
        utime.sleep(1)



# Function to get the current date and time adjusted to Greece, Athens Summer Time
def getTime():
    # Get current epoch time
    current_time = utime.time()
    
    # Convert epoch time to local time
    local_time = utime.localtime(current_time)
    
    # Format the time as a string
    time_str = "{:04d}-{:02d}-{:02d} {:02d}:{:02d}:{:02d}".format(
        local_time[0], local_time[1], local_time[2], local_time[3], local_time[4], local_time[5]
    )
    
    return time_str

# Function to send sensorData to Server

def send_sensor_data_to_server(pole_name, values):
    timestamp = getTime()
    sensor_data = {
        "SensorData": {
            "poleName": pole_name,
            "timestamp": timestamp,
            "values": values
        }
    }
    send_json_to_server(sensor_data)


# Function to send alarmData to Server

def send_alarm_data_to_server(pole_name, sensor_name,value):
    timestamp = getTime()
    alarm_data = {
        "AlarmData": {
            "poleName": pole_name,
            "timestamp": timestamp,
            "sensorName": sensor_name,
            "value":value
        }
    }
    send_json_to_server(alarm_data)


while True:
    check_Pir_status()
    manage_energy_saving()
    flame_alarm()
    
    received_data = receive_data()

    if received_data is not None:
        try:
            decoded_data = received_data.decode('utf-8')
            print(decoded_data)
            json_data = json.loads(decoded_data)
            
            if "SensorData" in json_data: 
                send_json_to_server(json_data)
            elif "AlarmData" in json_data:
                send_json_to_server(json_data)
            elif "ThingSpeak" in json_data:
                thingSpeak_data = rename_specific_keys(json_data)
                transformed_json = json.loads(thingSpeak_data)
                if transformed_json is not None:
                    print("Received data:", transformed_json)
                    request = urequests.post(
                        'http://api.thingspeak.com/update?api_key=' + THINGSPEAK_WRITE_API_KEY, 
                        json=transformed_json, 
                        headers={'Content-Type': 'application/json'}
                    )
                    request.close()
                
        except ValueError as e:
            print("JSON Decoding Error:", e)
        except Exception as e:
            print("MainError:", e)
    
    utime.sleep(1)
