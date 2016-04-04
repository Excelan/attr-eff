package com.attracti.app.gates.GateExampleNs;

import net.goldcut.gates.Gate;

import java.sql.Connection;
import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class GateExample implements Gate {

    // sql connection
    private Connection connection;
    // caller user + perms (pre gate run check, in gate check)

    public void setConnection(Connection connection)
    {
        this.connection =  connection;
    }

    public GateExample()
    {

    }

    private String sideIn(Connection connection) throws Exception
    {
        // load doc from db (DCT Instance Partial no relations)
        PreparedStatement doSelect = null;
        try
        {
            String contactEmail = null;
            String selectStatement = "SELECT address FROM company WHERE id = ?";
            doSelect = connection.prepareStatement(selectStatement);
            doSelect.setInt(1, 5);
            ResultSet rs = doSelect.executeQuery();
            if ( rs.next() )
            {
                contactEmail = rs.getString(1);
                System.out.println("FOUND: "+contactEmail);
                return contactEmail;
            }
            else
            {
                return null;
            }
        }
        catch (SQLException se)
        {
            throw se;
        }
        finally
        {
            doSelect.close();
        }
    }

    private void sideOut()
    {

    }

    // request - userid, docid
    public Response process(Request r)
    {
        String ret = null;
        try {
            ret = this.sideIn(this.connection);
        }
        catch (Exception e) {
            e.
                    printStackTrace();
        }

        System.out.println("--------- REQUEST");
        System.out.println(r.requrn);
        System.out.println(r.struct.isa);
        System.out.println(r.struct.isb);
        for (Request.StructMult rsm : r.structmult) {
            System.out.println(rsm.im);
        }

        this.sideOut();

        List<Response.StructMult> mlist = new ArrayList<>();
        mlist.add(new Response.StructMult("RMSr1"));
        mlist.add(new Response.StructMult("RMSr2"));
        mlist.add(new Response.StructMult("RMSr3"));
        Response.Struct struct = new Response.Struct("ISAr", "ISBr");

        return new Response(1, ret, struct, mlist);
    }

}