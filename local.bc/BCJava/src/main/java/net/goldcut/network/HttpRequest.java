package net.goldcut.network;

import java.io.*;
import java.net.HttpURLConnection;
import java.net.URL;
import javax.json.Json;
import javax.json.JsonObject;
import javax.json.JsonReader;

public class HttpRequest {


    public static String postGetString(String targetURL, String jsonString) throws Exception {
        String response = executePost(targetURL, jsonString);
        return response;
    }

    public static JsonObject postGetJsonObject(String targetURL, String jsonString) throws Exception {
        String response = executePost(targetURL, jsonString);
        System.out.println("RESPONSE FROM " + targetURL + " " + response);
        if (response == null) throw new Exception("No response from "+targetURL);
        JsonReader reader = Json.createReader(new StringReader(response));
        JsonObject j = reader.readObject();
        reader.close();
        return j;
    }

    private static String executePost(String targetURL, String jsonString) throws Exception {
        HttpURLConnection connection = null;
        String input = "json="+jsonString;
        try {
            //Create connection
            URL url = new URL(targetURL);
            connection = (HttpURLConnection)url.openConnection();
            connection.setUseCaches(false);
            connection.setDoOutput(true);
            connection.setRequestMethod("POST");
            connection.setRequestProperty("Content-Type", "application/x-www-form-urlencoded");
            //connection.setRequestProperty("Content-Language", "en-US");
            connection.setRequestProperty("Content-Length", Integer.toString(input.getBytes().length));

            //Send request
            DataOutputStream wr = new DataOutputStream(connection.getOutputStream());
            wr.writeBytes(input);
            wr.close();

            //Get Response x
            InputStream is;
            if (connection.getResponseCode() > 200)
                is = connection.getErrorStream();
            else
                is = connection.getInputStream();
            BufferedReader rd = new BufferedReader(new InputStreamReader(is));
            StringBuilder response = new StringBuilder(); // or StringBuffer if not Java 5+
            String line;
            while((line = rd.readLine()) != null) {
                response.append(line);
                //response.append('\r');
            }
            rd.close();
            if (connection.getResponseCode() > 200)
                throw new Exception("HTTP request " + targetURL + " " + jsonString + ". Error " + connection.getResponseCode() + " " + connection.getResponseMessage() + "\n " + response.toString());
            else
                return response.toString();
        } catch (Exception e) {
            //e.printStackTrace();
            //return null;
            throw e;
        } finally {
            if(connection != null) {
                connection.disconnect();
            }
        }
    }

}
