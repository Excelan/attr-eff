package experiments.server;

import java.io.IOException;
import java.util.Date;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.ManagedProcessesCentral;
import digital.erp.symbol.Prototype;
import digital.erp.symbol.UPN;
import digital.erp.symbol.URN;
import org.eclipse.jetty.server.Server;
import org.eclipse.jetty.servlet.ServletHandler;
import org.eclipse.jetty.continuation.*;

public class HttpServer
{
    public static void run() throws Exception
    {
        Server server = new Server(8020);
        ServletHandler handler = new ServletHandler();
        server.setHandler(handler);

        handler.addServletWithMapping(StartProcessServlet.class, "/startprocess/*");
        handler.addServletWithMapping(CompleteStageOfProcessServlet.class, "/completestage/*");
        // /completestage/?upn=UPN:ClaimsManagement:Claims:Claim:8889493

        // preload data before http start
        ManagedProcessesCentral mpc = ManagedProcessesCentral.getInstance();

        server.start();
        server.join();
    }

    @SuppressWarnings("serial")
    public static class StartProcessServlet extends HttpServlet
    {
        @Override
        protected void doGet( HttpServletRequest request, HttpServletResponse response ) throws ServletException, IOException
        {
            response.setContentType("application/json");
            response.setCharacterEncoding("UTF-8");
            response.setHeader("Access-Control-Allow-Origin", "*");

            String proto = null;
            String processProto = null;
            String subjectProto = null;
            URN initiator = null;
            UPN upn = null;
            Prototype subjectPrototype = null;
            try {
                proto = request.getParameter("prototype");
                //processProto = proto.split("/")[0];
                //subjectProto = proto.split("/")[1];
                initiator = new URN(request.getParameter("initiator"));
                if (request.getParameter("subjectPrototype") != null)
                {
                    subjectPrototype = Prototype.fromString(request.getParameter("subjectPrototype"));
                    // in mpe will be metadataJson = "{\"subjectPrototype\":\""+subjectPrototype.toString()+"\"}";
                    // будет использовано при создании черновика
                }

                try {

                    upn = ManagedProcessesCentral.getInstance().startProcess(Prototype.fromString(proto), initiator, null, subjectPrototype, null);
                    // TODO add metadata
                    response.getWriter().println("{\"upn\":\""+upn.toString()+"\"}");

                }
                catch (Exception e)
                {
                    response.setStatus(500);
                    //response.getWriter().println("{\"upn\":\""+upn.toString()+"\", \"state\":\"ERROR\", \"details\":\""+e.getMessage()+"\"}");
                    System.err.println("proto " + proto + " subjectPrototype " + subjectPrototype);
                    System.err.println(e.getMessage());
                    e.printStackTrace();
                }

            }
            catch (Exception e)
            {
                //response.setStatus(HttpServletResponse.SC_BAD_REQUEST);
                response.setStatus(500);
                e.printStackTrace();
                //return;
            }
            finally
            {
                response.getWriter().flush();
            }



        }
    }

    @SuppressWarnings("serial")
    public static class CompleteStageOfProcessServlet extends HttpServlet
    {
        @Override
        protected void doGet( HttpServletRequest request, HttpServletResponse response ) throws ServletException, IOException
        {
            response.setContentType("application/json");
            response.setCharacterEncoding("UTF-8");
            response.setHeader("Access-Control-Allow-Origin", "*");

            UPN upn = null;
            try {
                upn = new UPN(request.getParameter("upn"));

            }
            catch (Exception e)
            {
                response.setStatus(HttpServletResponse.SC_BAD_REQUEST);
                e.printStackTrace();
                return;
            }

            try
            {

                ManagedProcessExecution mpe = ManagedProcessExecution.load(upn);
                mpe.completeCurrentStage();

                response.getWriter().println("{\"upn\":\""+upn.toString()+"\"}");

            }
            catch (Exception e)
            {
                response.setStatus(500);
                response.getWriter().println("{\"upn\":\""+upn.toString()+"\", \"state\":\"ERROR\", \"details\":\""+e.getMessage()+"\"}");
                e.printStackTrace();
            }
            finally
            {
                response.getWriter().flush();
            }
        }
    }
}
