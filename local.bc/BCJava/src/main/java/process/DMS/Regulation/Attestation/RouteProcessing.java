package process.DMS.Regulation.Attestation;

import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageProcessing;
import net.goldcut.network.HttpRequest;
import net.goldcut.utils.Configuration;

public class RouteProcessing implements StageProcessing {

    public void process(ManagedProcessExecution mpe)
    {

        /*
        try {
            String json = "{ \"mpeId\":\"" + mpe.getUPN().getId() + "\" }";
            String gout = HttpRequest.postGetString(Configuration.host()+"/processrouter/DMSRegulationAttestation", json);
            System.out.println("ROUTE OK " + gout);
        } catch (Exception e) {
            System.err.println("ROUTE ERRROR");
            System.err.println(e.getMessage());
            e.printStackTrace();
        }
        */

    }

}
