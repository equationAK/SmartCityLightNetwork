
package com.httpserver.core;
import com.http.HttpParser;
import com.http.HttpRequest;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.io.*;
import java.net.Socket;

public class HttpConnectionWorkerThread extends Thread {
    private final static Logger LOGGER = LoggerFactory.getLogger(HttpConnectionWorkerThread.class);
    private Socket socket;

    public HttpConnectionWorkerThread(Socket socket) {
        this.socket = socket;
    }

    @Override
    public void run() {
        InputStream inputStream = null;
        OutputStream outputStream = null;

        try {
            inputStream = socket.getInputStream();
            outputStream = socket.getOutputStream();

            HttpRequest httpRequest = new HttpRequest();
            httpRequest.httpSerializer(inputStream);

            String message = "The Server responded:";

            final String CRLF = "\r\n"; // 13, 10

            // Status Line  :   HTTP_VERSION RESPONSE_CODE RESPONSE_MESSAGE
            // HEADER
            String response = STR."HTTP/1.1 200 OK\{CRLF}Content-Length: \{message.getBytes().length}\{CRLF}\{CRLF}\{message}\{CRLF}\{CRLF}";

            outputStream.write(response.getBytes());
            LOGGER.info(" * Connection Processing Finished.");

            System.out.printf("Method : %s\nURL : %s\n",httpRequest.getMethod(),httpRequest.getUrl());
            httpRequest.getHeaders().forEach((key,value)-> System.out.printf("%s : %s\n",key,value));
            System.out.println("=".repeat(50));
            System.out.printf("Http Request Body : %s\n",httpRequest.getHttpBody());
            System.out.println("=".repeat(50));

            HttpParser httpParser = new HttpParser(httpRequest);
            httpParser.bodyToJsonConvert(httpRequest.getHttpBody());

        } catch (IOException e) {
            LOGGER.error("Problem with communication", e);
        } finally {
            if (inputStream != null) {
                try {
                    inputStream.close();
                } catch (IOException e) {}
            }
            if (outputStream != null) {
                try {
                    outputStream.close();
                } catch (IOException e) {}
            }
            if (socket != null) {
                try {
                    socket.close();
                } catch (IOException e) {}
            }
        }
    }


    public String readSocketInput(InputStream inputStream) throws IOException {

        InputStreamReader inputStreamReader = new InputStreamReader(inputStream);
        BufferedReader bufferedReader = new BufferedReader(inputStreamReader);

        StringBuilder stringBuilder = new StringBuilder();
        String line;

        while ((line = bufferedReader.readLine()) != null) {
            stringBuilder.append(line).append("\n");
        }

        return stringBuilder.toString();
    }
}