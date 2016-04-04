package process.DMS.Regulation.Attestation;

import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageIn;
import net.goldcut.network.HttpRequest;
import net.goldcut.utils.Configuration;

public class PlaningIn implements StageIn {

    public void process(ManagedProcessExecution processExecution)
    {

/*
            System.out.println("!!! > DMS Create tickets testing");
            try {
                String json = "{ \"mpeId\":\"" + processExecution.getUPN().getId() + "\" }";
                String gout = HttpRequest.postGetString(Configuration.host()+"/Process/CreateTicketsForPlaning", json);
                System.out.println("REMAP tickets testing OK " + gout);
            } catch (Exception e) {
                System.err.println("REMAP tickets testing ERROR");
                System.err.println(e.getMessage());
                e.printStackTrace();
            }
*/

    }

}
