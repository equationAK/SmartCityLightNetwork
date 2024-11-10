package com.http;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.util.HashMap;
import java.util.Map;

public class HttpRequest {

    private String method;
    private String url;
    private Map<String, String> headers = new HashMap<>();

    private String httpBody;
    public HttpRequest() {
    }

    public String getMethod() {
        return method;
    }

    public String getUrl() {
        return url;
    }

    public Map<String, String> getHeaders() {
        return headers;
    }

    public String getHttpBody() {
        return httpBody;
    }

    public void httpSerializer(InputStream inputStream) throws IOException {
        InputStreamReader reader = new InputStreamReader(inputStream);
        BufferedReader bufferedReader = new BufferedReader(reader);
        parseMetaData(bufferedReader);
        parseBody(bufferedReader);
    }

    private void parseMetaData(BufferedReader bufferedReader) throws IOException {
        String firstLine = bufferedReader.readLine();
        this.method = firstLine.split("\\s+")[0];
        this.url = firstLine.split("\\s+")[1];

        String headerLine;
        while ((headerLine = bufferedReader.readLine()) != null) {
            if (headerLine.trim().isEmpty()) {
                break;
            }
            String key = headerLine.split(":\\s")[0];
            String value = headerLine.split(":\\s")[1];
            this.headers.put(key,value);
        }
    }

    private void parseBody(BufferedReader bufferedReader) throws IOException {
        StringBuilder bodyBuilder = new StringBuilder();
        int contentLength = getContentLength();
        if (contentLength > 0) {
            char[] bodyBuffer = new char[contentLength];
            bufferedReader.read(bodyBuffer, 0, contentLength);
            bodyBuilder.append(bodyBuffer);
        }
        this.httpBody = bodyBuilder.toString();
    }

    private int getContentLength() {
        String contentLengthValue = this.headers.get("Content-Length");
        if (contentLengthValue != null && !contentLengthValue.isEmpty()) {
            try {
                return Integer.parseInt(contentLengthValue);
            } catch (NumberFormatException e) {
                e.printStackTrace(); // Handle parsing error
            }
        }
        return 0;
    }
}
