package systest;

//import org.junit.Assert;
//import org.junit.Ignore;
//import org.junit.Test;
import net.goldcut.network.HttpRequest;
import net.goldcut.utils.*;
import org.testng.annotations.*;
import org.testng.Assert;

import digital.erp.symbol.URN;

import javax.json.JsonObject;
import java.io.*;
import java.net.HttpURLConnection;
import java.net.URL;

//@Ignore
public class ERPTestBase extends Assert {

    //@Test
    public void httpTest2() throws Exception
    {
        String r = HttpRequest.postGetString(net.goldcut.utils.Configuration.host()+"/echopost", "{\"qty\":100,\"name\":\"iPad 4\"}");
        System.out.println(r);
        assertEquals(r, "{\"name\":\"iPad 4\"}");

        JsonObject json = HttpRequest.postGetJsonObject(net.goldcut.utils.Configuration.host()+"/echopost", "{\"qty\":100,\"name\":\"iPad 4\"}");
        System.out.println(json);
        assertEquals(json.getString("name"), "iPad 4");
    }

    @Test
    public void testURNDomain() throws Exception {
        URN urn = new URN("urn:business:structure:company:17");
        assertEquals(urn.getPrototype().getInDomain(), "business");
    }

    @Test
    public void testURNClass() throws Exception {
        URN urn = new URN("urn:business:structure:company:17");
        assertEquals(urn.getPrototype().getOfClass(), "structure");
    }

    @Test
    public void testURNType() throws Exception {
        URN urn = new URN("urn:business:structure:company:17");
        assertEquals(urn.getPrototype().getOfType(), "company");
    }

    /**
    @Test(expected = URNExceptions.IncorrectFormat.class)
    public void testIncorrectURN() throws URNExceptions.IncorrectFormat {
        new URN("urn-business..");
    }
    */

    /**
    @Test
    public void badTestURN() throws Exception {
        new URN("urn-business-stru..");
    }
    */

}