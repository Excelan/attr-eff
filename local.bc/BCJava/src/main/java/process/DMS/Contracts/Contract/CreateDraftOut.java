package process.DMS.Contracts.Contract;

import digital.erp.data.Entity;
import digital.erp.domains.document.Document;
import digital.erp.domains.document.DocumentMetadata;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageOut;
import net.goldcut.network.HttpRequest;
import net.goldcut.utils.Configuration;

public class CreateDraftOut implements StageOut {

    public void process(ManagedProcessExecution mpe)
    {
        System.out.println(mpe.toString());

        // Tickets раздаем здесь (исполняет процесс уже Actor System User 0!!!)
        try {
            String json = "{ \"mpeId\":\"" + mpe.getUPN().getId() + "\" }";
            String gout = HttpRequest.postGetString(Configuration.host()+"/Events/AutoCreateField", json);
            System.out.println("REMAP DeactiveTicketsForCorrection OK " + gout);
        } catch (Exception e) {
            System.err.println("REMAP Delegates ERRROR");
            System.err.println(e.getMessage());
            e.printStackTrace();
        }
    }

}
