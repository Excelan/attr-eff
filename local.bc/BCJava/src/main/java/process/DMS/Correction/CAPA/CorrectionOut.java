package process.DMS.Correction.CAPA;

import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageOut;
import net.goldcut.network.HttpRequest;
import net.goldcut.utils.Configuration;

public class CorrectionOut implements StageOut {

    public void process(ManagedProcessExecution processExecution)
    {
        System.out.println(processExecution.toString());

        // Tickets раздаем здесь (исполняет процесс уже Actor System User 0!!!)
        try {
            String json = "{ \"mpeId\":\"" + processExecution.getUPN().getId() + "\" }";
            String gout = HttpRequest.postGetString(Configuration.host()+"/capadirector/DeactiveTicketsForCorrection", json);
            System.out.println("REMAP DeactiveTicketsForCorrection OK " + gout);
        } catch (Exception e) {
            System.err.println("REMAP Delegates ERRROR");
            System.err.println(e.getMessage());
            e.printStackTrace();
        }
    }

}
