package process.DMS.Correction.CAPAInspection;

import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageIn;
import net.goldcut.network.HttpRequest;
import net.goldcut.utils.Configuration;

public class EditingIIn implements StageIn {

    public void process(ManagedProcessExecution processExecution)
    {


        // Tickets раздаем здесь (исполняет процесс уже Actor System User 0!!!)
        try {
            String json = "{ \"mpeId\":\"" + processExecution.getUPN().getId() + "\" }";
            String gout = HttpRequest.postGetString(Configuration.host()+"/capadirector/CreateTicketsForCapaInspectionEditing", json);
            System.out.println("REMAP Delegates OK " + gout);
        } catch (Exception e) {
            System.err.println("REMAP Delegates ERRROR");
            System.err.println(e.getMessage());
            e.printStackTrace();
        }


    }

}
