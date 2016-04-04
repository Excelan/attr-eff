package process.DMS.Decisions.Plan;

import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageIn;
import net.goldcut.network.HttpRequest;
import net.goldcut.utils.Configuration;

public class PlanningIn  implements StageIn {

    public void process(ManagedProcessExecution processExecution)
    {
        System.out.println("!!! > Planning In (appoint=configuration)");
        /**
        try {
            UPN returntopmeUPN = mpe.getReturntopme();
            if (returntopmeUPN != null) {
                ManagedProcessExecution returntopme = ManagedProcessExecution.load(returntopmeUPN);
                String json = "{ \"postURN\":\"" + returntopme.getInitiator() + "\", \"mpeId\":\"" + mpe.getUPN().getId() + "\" }";
                System.out.println("REMAP? " + json);
                String gout = HttpRequest.postGetString("http://local.bc/Process/CreateTicketFor", json);
                System.out.println("REMAP PLANNER OK " + gout);
            } else
                System.err.println("NO PARENT MPE");
        } catch (Exception e) {
            System.err.println("REMAP PLANNER ERROR");
            System.err.println(e.getMessage());
            e.printStackTrace();
        }
         */




         System.out.println("!!! > DMS Create tickets testing");
         try {
             String json = "{ \"mpeId\":\"" + processExecution.getUPN().getId() + "\" }";
             String gout = HttpRequest.postGetString(Configuration.host()+"/Process/CreateTicketsForPlaning", json);
             System.out.println("REMAP tickets OK " + gout);
         } catch (Exception e) {
             System.err.println("REMAP tickets ERROR ");
             System.err.println(e.getMessage());
             e.printStackTrace();
         }







    }

}