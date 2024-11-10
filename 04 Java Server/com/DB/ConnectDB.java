package com.DB;



import com.DB.configurationDB.Config;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.SQLException;


public class ConnectDB {


    //create an object of ConnectDB
    private static ConnectDB connectionInstance = new ConnectDB();

    //make the constructor private so that this class cannot be instantiated
    private ConnectDB() {
    }

    //Get the only object available
    public static ConnectDB getConnectionInstance() {
        if (connectionInstance == null)
            connectionInstance = new ConnectDB();
        return connectionInstance;
    }

    // TODO fix the connect() method with the correct parameters of the final DB server

    public Connection connect() {

        String connectionString = "jdbc:mysql://".concat(Config.ADDRESS).concat(":").concat(String.valueOf(Config.PORT)).concat("/testGetPost?useSSL=false");  // this should change to the final DB name
        Connection connection = null;
        try {
            connection = DriverManager.getConnection(connectionString, Config.USER, Config.PASSWORD);
            if (connection != null)
                System.out.println("Connection to DataBase established!");

            // Step 2:Create a statement using connection object
            //Statement statement = connection.createStatement();

            // Step 3: Execute the query or update query

            //statement.execute(createTableSQL);

        } catch (SQLException throwables) {
            throwables.printStackTrace();
        }
        return connection;
    }

}