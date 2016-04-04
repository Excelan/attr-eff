package process.DMS.Decisions.Approvement;

import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageIn;
import net.goldcut.network.HttpRequest;
import net.goldcut.utils.Configuration;

public class ApproveIn implements StageIn {

    public void process(ManagedProcessExecution mpe)
    {
        System.out.println("!!! > DMS Approvement Approve In2");
        try {
            String json = "{ \"mpeId\":\"" + mpe.getUPN().getId() + "\" }";
            String gout = HttpRequest.postGetString(Configuration.host()+"/Process/CreateTicketForApprover", json);
            System.out.println("REMAP APPROVER OK " + gout);
        } catch (Exception e) {
            System.err.println("REMAP APPROVER ERROR");
            System.err.println(e.getMessage());
            e.printStackTrace();
        }
    }
}