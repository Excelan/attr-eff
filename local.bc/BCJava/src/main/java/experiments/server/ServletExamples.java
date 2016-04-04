package experiments.server;

import digital.erp.process.ManagedProcessesCentral;
import digital.erp.symbol.Prototype;
import digital.erp.symbol.UPN;
import digital.erp.symbol.URN;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.IOException;

public class ServletExamples {

    public static class StartProcessServlet extends HttpServlet
    {
        @Override
        protected void doGet(HttpServletRequest request, HttpServletResponse response ) throws ServletException, IOException
        {
//            response.setContentType("text/html");
//            response.setStatus(HttpServletResponse.SC_OK);
//            response.getWriter().println("<h1>Hello from HelloServlet</h1>");

            String proto = null;
            URN initiator = null;
            Prototype subjectPrototype = null;
            try {
                proto = request.getParameter("prototype");
                //Integer initiator = Integer.parseInt(request.getParameter("initiator"));
                initiator = new URN(request.getParameter("initiator"));
                if (request.getParameter("subjectPrototype") != null) {
                    //URN subjectProtoURN = new URN(request.getParameter("subjectPrototype"));
                    //subjectPrototype = subjectProtoURN.getPrototype();
                    subjectPrototype = Prototype.fromString(request.getParameter("subjectPrototype"));
                }
            }
            catch (Exception e)
            {
                response.setStatus(HttpServletResponse.SC_BAD_REQUEST);
                e.printStackTrace();
                return;
            }

            try {
                UPN upn = ManagedProcessesCentral.getInstance().startProcess(Prototype.fromString(proto), initiator, null, subjectPrototype, null);
                //System.out.println(request.getQueryString() + request.getRequestURL());
                //Continuation cc = ContinuationSupport.getContinuation(request);
                response.setContentType("text/plain");
                response.getWriter().println("Process start: " + proto + "\tstart:\t <a href='/completestage/?upn="+upn.toString()+"'>" + upn.toString() + "</a>");
                response.getWriter().flush();
            }
            catch (Exception e)
            {
                e.printStackTrace();
            }

            /*
            Thread currThread = Thread.currentThread();
            System.out.println("current thread = " + currThread + " req id " + reqId);

            cc.setTimeout(10);
            cc.suspend();

            currThread = Thread.currentThread();
            System.out.println("current thread = " + currThread + " req id " + reqId);

            response.getWriter().println("GateRequest: " + reqId + "\tend:\t" + new Date());
            if (cc.isInitial() != true) {
                currThread = Thread.currentThread();
                System.out.println("current thread = " + currThread + " req id " + reqId);
                cc.complete();
                System.out.println("COMPLETE");
            }
            System.out.println("AFTER COMPLETE");
            */
        }
    }

}
