package experiments.feature;

import java.io.StringReader;
import java.io.StringWriter;
import javax.json.Json;
import javax.json.JsonArray;
import javax.json.JsonObject;
import javax.json.JsonReader;
import javax.json.JsonValue;
import javax.json.JsonObjectBuilder;
import javax.json.JsonWriter;

// http://www.infoq.com/news/2014/09/CoreJSONJava9
// http://www.infoq.com/news/2013/04/standard-java-api-for-json
//

public class JsonExample {
    public static void run1() {
        String personJSONData =
            "  {" +
            "   \"name\": \"Jack\", " +
            "   \"age\" : 13, " +
            "   \"isMarried\" : false, " +
            "   \"address\": { " +
            "     \"street\": \"#1234, Main Street\", " +
            "     \"zipCode\": \"123456\" " +
            "   }, " +
            "   \"phoneNumbers\": [\"011-111-1111\", \"11-111-1111\"] " +
            " }";

        JsonReader reader = Json.createReader(new StringReader(personJSONData));

        JsonObject personObject = reader.readObject();

        reader.close();

        System.out.println("Name   : " + personObject.getString("name"));
        System.out.println("Age    : " + personObject.getInt("age"));
        System.out.println("Married: " + personObject.getBoolean("isMarried"));

        JsonObject addressObject = personObject.getJsonObject("address");
        System.out.println("Address: ");
        System.out.println(addressObject.getString("street"));
        System.out.println(addressObject.getString("zipCode"));

        System.out.println("Phone  : ");
         JsonArray phoneNumbersArray = personObject.getJsonArray("phoneNumbers");
        for (JsonValue jsonValue : phoneNumbersArray) {
            System.out.println(jsonValue.toString());
        }

        System.out.println("------");

        personJSONData =
                "  [{" +
                        "   \"name\": \"Test\", " +
                        "   \"age\" : 13, " +
                        "   \"phoneNumbers\": [\"011-111-1111\", \"11-111-1111\"] " +
                        " }]";

        reader = Json.createReader(new StringReader(personJSONData));
        JsonArray personArray = reader.readArray();
        reader.close();
        for (JsonValue personObj : personArray) {
            System.out.println(personObj.getValueType() + " - " + ((JsonObject) personObj).getString("name"));
        }
    }

    public static void run2()
    {
        JsonObject personObject = Json.createObjectBuilder()
                .add("name", "John")
                .add("age", 13)
                .add("isMarried", false)
                .add("address",
                        Json.createObjectBuilder().add("street", "Main ТЕСТ Street")
                                .add("city", "New York")
                                .add("zipCode", "11111")
                                .build()
                )
                .add("phoneNumber",
                        Json.createArrayBuilder().add("00-000-0000")
                                .add("11-111-1111")
                                .add("11-111-1112")
                                .build()
                )
                .build();

        System.out.println("------RUN2");
        System.out.println("Object: " + personObject);

        StringWriter stringWriter = new StringWriter();
        JsonWriter writer = Json.createWriter(stringWriter);
        writer.writeObject(personObject);
        writer.close();
        System.out.println(stringWriter.getBuffer().toString());
    }

    /**
     * http://www.journaldev.com/2315/java-json-processing-api-example-tutorial
     *  Employee emp = new Employee();
     emp.setId(jsonObject.getInt("id"));
     emp.setName(jsonObject.getString("name"));
     emp.setPermanent(jsonObject.getBoolean("permanent"));
     emp.setRole(jsonObject.getString("role"));

     1 URL url = new URL("https://graph.facebook.com/search?q=java&type=post");
     2 try (InputStream is = url.openStream();
     3      JsonReader rdr = Json.createReader(is)) {
     4
     5     JsonObject obj = rdr.readObject();
     6     JsonArray results = obj.getJsonArray("data");
     7     for (JsonObject result : results.getValuesAs(JsonObject.class)) {
     8         System.out.print(result.getJsonObject("from").getString("name"));
     9         System.out.print(": ");
     10         System.out.println(result.getString("message", ""));
     11         System.out.println("-----------");
     12     }
     13 }
     Listin
     */

}
