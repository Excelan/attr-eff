package process.DMS.Regulation.Attestation;

import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageOut;
import net.goldcut.network.HttpRequest;
import net.goldcut.utils.Configuration;

public class PlaningOut implements StageOut {

    public void process(ManagedProcessExecution processExecution)
    {

/*
                System.out.println("!!! > DMS Create tickets testing");
                try {
                    String json = "{ \"mpeId\":\"" + processExecution.getUPN().getId() + "\" }";
                    String gout = HttpRequest.postGetString(Configuration.host()+"/Process/CreateTicketsForTesting", json);
                    System.out.println("REMAP APPROVER OK " + gout);
                } catch (Exception e) {
                    System.err.println("REMAP APPROVER ERROR");
                    System.err.println(e.getMessage());
                    e.printStackTrace();
                }
*/

    }

}
