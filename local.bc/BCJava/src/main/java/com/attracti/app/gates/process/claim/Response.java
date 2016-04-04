package com.attracti.app.gates.process.claim;

import net.goldcut.gates.GateResponse;
import java.io.StringWriter;
import javax.json.*;

public class Response implements GateResponse {

    public final int i;
    public final String s;

    Response(int i, String s)
    {
        this.i = i;
        this.s = s;
    }

    public String toJSON()
    {
        JsonObjectBuilder ob = Json.createObjectBuilder();
        ob.add("i", i);
        ob.add("s", s);
        JsonObject JSON = ob.build();

        StringWriter stringWriter = new StringWriter();
        JsonWriter writer = Json.createWriter(stringWriter);
        writer.writeObject(JSON);
        writer.close();

        return stringWriter.getBuffer().toString();
    }
}