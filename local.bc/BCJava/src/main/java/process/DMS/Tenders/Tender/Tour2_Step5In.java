package process.DMS.Tenders.Tender;

import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageIn;
import net.goldcut.network.HttpRequest;
import net.goldcut.utils.Configuration;

public class Tour2_Step5In implements StageIn {

    public void process(ManagedProcessExecution mpe)
    {
        System.out.println(mpe.toString());

        // Tickets раздаем здесь (исполняет процесс уже Actor System User 0!!!)
        try {
            String json = "{ \"mpeId\":\"" + mpe.getUPN().getId() + "\" }";
            String gout = HttpRequest.postGetString(Configuration.host()+"/tenderdirector/BindingPositionToParticipants", json);
            System.out.println("REMAP DeactiveTicketsForCorrection OK " + gout);
        } catch (Exception e) {
            System.err.println("REMAP Delegates ERRROR");
            System.err.println(e.getMessage());
            e.printStackTrace();
        }
    }

}
