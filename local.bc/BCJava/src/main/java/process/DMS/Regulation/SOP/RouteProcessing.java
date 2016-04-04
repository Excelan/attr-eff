package process.DMS.Regulation.SOP;

import digital.erp.data.Entity;
import digital.erp.data.EntityMetadata;
import digital.erp.domains.document.Document;
import digital.erp.domains.document.DocumentMetadata;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageProcessing;
import net.goldcut.network.HttpRequest;
import net.goldcut.utils.Configuration;

public class RouteProcessing implements StageProcessing {

    public void process(ManagedProcessExecution mpe)
    {
        //DocumentMetadata dmd = Document.loadDocumentMetadata(mpe.getSubject());
        //EntityMetadata emd = Entity.loadEntityMetadata(mpe.getSubject());
        //Entity.directUpdateLong(mpe.getSubject(), "DocumentComplaint", 1);
        //Entity.directLoadStringForKeyIn("", mpe.getSubject());
        //mpe.setMetadataKeyValue("parentSubject", parentSubject);
        //Entity.directLoadURNListForKeyIn("", mpe.getSubject());

        /*
        try {
            String json = "{ \"mpeId\":\"" + mpe.getUPN().getId() + "\" }";
            String gout = HttpRequest.postGetString(Configuration.host()+"/processrouter/DMSRegulationSOP", json);
            System.out.println("ROUTE OK " + gout);
        } catch (Exception e) {
            System.err.println("ROUTE ERRROR");
            System.err.println(e.getMessage());
            e.printStackTrace();
        }
        */

    }

}
