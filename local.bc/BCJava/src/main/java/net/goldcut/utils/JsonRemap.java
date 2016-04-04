package net.goldcut.utils;

import javax.json.JsonArray;
import javax.json.JsonObject;
import javax.json.JsonString;
import javax.json.JsonValue;
import java.util.Map;
import java.util.Set;

public class JsonRemap {

    public static void remap(Object container, JsonObject j) {
        Set<Map.Entry<String, JsonValue>> tlo = j.entrySet();
        tlo.forEach((kv)-> {
            String k = kv.getKey();
            JsonValue v = kv.getValue();
            if ("STRING".equals(v.getValueType().toString()))
            {
                JsonString s = (JsonString) v;
                MetaProgrammingUtils.set(container, k, s.getString());
            }
            if ("NUMBER".equals(v.getValueType().toString()))
            {
                MetaProgrammingUtils.set(container, k, v.toString());
            }
            else if ("OBJECT".equals(v.getValueType().toString()) && ((JsonObject)v).entrySet().size() == 2 ) { // Unit objects (urn, title)
                JsonObject vo = (JsonObject) v;
                if (!vo.isNull("title"))
                    MetaProgrammingUtils.set(container, k, vo.getString("title"));
            }
        });
    }

    /**
     * JsonObject j = HttpRequest.postGetJsonObject("http://local.bc/universalload/Contract/Decision/Contract/BW", "{\"urn\":\"urn:Document:Contract:BW:118372153\"}");
    // iterate json top level object
    Set<Map.Entry<String, JsonValue>> tlo = j.entrySet();
    tlo.forEach((kv)-> {
        String k = kv.getKey();
        JsonValue v = kv.getValue();
        if ("STRING".equals(v.getValueType().toString()) || "NUMBER".equals(v.getValueType().toString()))
        {
            MetaProgrammingUtils.set(info, k, v.toString());
        }
        else if ("OBJECT".equals(v.getValueType().toString()) && ((JsonObject)v).entrySet().size() == 2 ) { // Unit objects (urn, title)
            JsonObject vo = (JsonObject) v;
            if (!vo.isNull("title"))
                MetaProgrammingUtils.set(info, k, vo.getString("title"));
        }
        else if ("OBJECT".equals(v.getValueType().toString())) { // Inlined Full Object
            JsonObject vo = (JsonObject) v;
            if (!vo.isNull("title"))
                MetaProgrammingUtils.set(info, k, vo.getString("title"));
        }
        else if ("ARRAY".equals(v.getValueType().toString())) {
            // todo cast fix
            JsonObject vo = (JsonObject) v;
            JsonArray jsonArrayIn = vo.getJsonArray(k);
            //JsonArray jsonArrayIn = (JsonArray) v;
            if (jsonArrayIn != null) {
                for (int maidx = 0; maidx < jsonArrayIn.size(); maidx++) {
                    System.out.println("GET  " + maidx + " FROM " + jsonArrayIn.size());
                    JsonObject objectin = jsonArrayIn.getJsonObject(maidx);
                    System.out.println(objectin);
                    Set<Map.Entry<String, JsonValue>> ma = objectin.entrySet();
                    System.out.println(ma);
                    ma.forEach((kvi)-> {
                        String ki = kvi.getKey();
                        JsonValue vi = kvi.getValue();
                        System.out.println("Inner Item : " + ki + " V : " + vi);
                    });
                }
            }

        }
    });
     */

}
