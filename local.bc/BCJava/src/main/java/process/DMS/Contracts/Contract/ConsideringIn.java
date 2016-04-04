package process.DMS.Contracts.Contract;

import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageIn;
import net.goldcut.network.HttpRequest;
import net.goldcut.utils.Configuration;

public class ConsideringIn implements StageIn {

    public void process(ManagedProcessExecution mpe)
    {
        System.out.println(mpe.toString());

        // Tickets раздаем здесь
        try {
            String json = "{ \"mpeId\":\"" + mpe.getUPN().getId() + "\" }";
            String gout = HttpRequest.postGetString(Configuration.host()+"/contractdirector/CreateTicketsForConsidering", json);
            System.out.println("REMAP TicketsForCorrection OK " + gout);
        } catch (Exception e) {
            System.err.println("REMAP Delegates ERRROR");
            System.err.println(e.getMessage());
            e.printStackTrace();
        }
    }

}
