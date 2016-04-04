package com.attracti.app.gates.process.claim;

public class ClaimStartProcess {

    public Response process(Request r)
    {

        System.out.println("--------- ClaimStartProcess REQUEST");
        System.out.println(r.requrn);

        return new Response(1, r.requrn.toUpperCase());
    }

}
