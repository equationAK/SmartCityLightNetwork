package com.http;

public abstract class HttpMessage {

    private HttpMethod method;
    private String requestTarget;
    private String httpVersion;

    HttpMessage() {

    }

    public HttpMethod getMethod() {
        return method;
    }

    public void setMethod(HttpMethod method) {
        this.method = method;
    }

}
