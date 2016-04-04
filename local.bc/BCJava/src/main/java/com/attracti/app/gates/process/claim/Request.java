package com.attracti.app.gates.process.claim;

import net.goldcut.gates.GateRequest;
import javax.json.*;
import java.io.StringReader;

public class Request implements GateRequest {

    public final String requrn;

    public Request(String requrn)
    {
        this.requrn = requrn;
    }

    public static Request fromJSON(String jsonstring)
    {
        JsonReader reader = Json.createReader(new StringReader(jsonstring));
        JsonObject j = reader.readObject();
        reader.close();

        return new Request(j.getString("requrn"));
    }
}
