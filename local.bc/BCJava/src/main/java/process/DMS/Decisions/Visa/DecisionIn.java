package process.DMS.Decisions.Visa;

import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageIn;
import digital.erp.symbol.URN;
import net.goldcut.network.HttpRequest;
import net.goldcut.utils.Configuration;

public class DecisionIn implements StageIn {

    public void process(ManagedProcessExecution processExecution)
    {
        System.out.println("!!! > DMS Visa Decision In");
        System.out.println(processExecution.toString());

        // Tickets раздаем здесь
        // Sheet создан один раз на этапе Document create draft
        try {
            //String json = "{\"subjectURN\":\"" + processExecution.getSubject().toString() + "\", \"mpeId\":\"" + processExecution.getUPN().getId() + "\", \"rand\":\"" + URN.randomLong() + "\"}";
            String json = "{ \"mpeId\":\"" + processExecution.getUPN().getId() + "\" }";
            String gout = HttpRequest.postGetString(Configuration.host()+"/Process/CreateActiveSheetAndTicketsForVisants", json);
            System.out.println("REMAP VISANTS OK " + gout);
        } catch (Exception e) {
            System.err.println("REMAP VISANTS ERRROR");
            System.err.println(e.getMessage());
            e.printStackTrace();
        }
    }
}