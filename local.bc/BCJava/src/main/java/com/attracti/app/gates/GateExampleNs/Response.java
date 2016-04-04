package com.attracti.app.gates.GateExampleNs;

import net.goldcut.gates.GateResponse;
import java.io.StringWriter;
import java.util.List;
import javax.json.*;

public class Response implements GateResponse {

    public final int i;
    public final String s;
    public static class Struct {
        public String isa;
        public String isb;
        public Struct(String isa, String isb)
        {
            this.isa = isa;
            this.isb = isb;
        }
    }
    public final Struct struct;

    public static class StructMult {
        public String im;
        public StructMult(String im)
        {
            this.im = im;
        }
    }
    public final List<StructMult> structmult;

    Response(int i, String s, Struct struct, List<StructMult> structmult)
    {
        this.i = i;
        this.s = s;
        this.struct = struct;
        this.structmult = structmult;
    }

    public String toJSON()
    {
        JsonObjectBuilder ob = Json.createObjectBuilder();
        ob.add("i", i);
        ob.add("s", s);
        JsonObjectBuilder sb = Json.createObjectBuilder();
        sb.add("isa", struct.isa);
        sb.add("isb", struct.isb);
        ob.add("struct", sb);
        JsonArrayBuilder sbm = Json.createArrayBuilder();
        for (StructMult smitem : structmult)
        {
            JsonObjectBuilder smitemb = Json.createObjectBuilder();
            smitemb.add("im", smitem.im);
            sbm.add(smitemb);
        }
        ob.add("structmult", sbm);
        JsonObject JSON = ob.build();

        StringWriter stringWriter = new StringWriter();
        JsonWriter writer = Json.createWriter(stringWriter);
        writer.writeObject(JSON);
        writer.close();

        return stringWriter.getBuffer().toString();
    }
}
