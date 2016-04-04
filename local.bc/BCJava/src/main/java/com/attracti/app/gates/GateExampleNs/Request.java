package com.attracti.app.gates.GateExampleNs;

import net.goldcut.gates.GateRequest;
import java.io.StringReader;
import java.util.ArrayList;
import java.util.List;
import javax.json.Json;
import javax.json.JsonArray;
import javax.json.JsonObject;
import javax.json.JsonReader;

public class Request implements GateRequest {

    public final String requrn;

    public static class Struct {
        public String isa;
        public String isb;
    }
    public final Struct struct;

    public static class StructMult {
        public String im;
    }
    public final List<StructMult> structmult;

    public Request(String requrn, Struct struct, List<StructMult> structmult)
    {
        this.requrn = requrn;
        this.struct = struct;
        this.structmult = structmult;
    }

    public static Request fromJSON(String jsonstring)
    {
        JsonReader reader = Json.createReader(new StringReader(jsonstring));
        JsonObject j = reader.readObject();
        reader.close();

        JsonObject jstruct = j.getJsonObject("struct");
        Request.Struct struct = new Request.Struct();
        struct.isa = jstruct.getString("isa");
        struct.isb = jstruct.getString("isb");

        JsonArray jsonArray = j.getJsonArray("structmult");
        List<StructMult> structmult = new ArrayList<StructMult>();
        for(int n = 0; n < jsonArray.size(); n++)
        {
            JsonObject object = jsonArray.getJsonObject(n);
            Request.StructMult structm = new Request.StructMult();
            structm.im = object.getString("im");
            structmult.add(structm);
        }

        return new Request(j.getString("requrn"), struct, structmult);
    }
}
