package process.DMS.Tenders.Tender;

import digital.erp.process.ManagedProcessException;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageOut;
import net.goldcut.network.HttpRequest;
import net.goldcut.utils.Configuration;

import java.util.Objects;

public class Tour1_Step3Out implements StageOut {

    public void process(ManagedProcessExecution mpe)
    {
        System.out.println(mpe.toString());

        /*
        try {
            mpe.setNextstage("Tour1_Step2");
        } catch (ManagedProcessException.StageNotExists stageNotExists) {
            stageNotExists.printStackTrace();
        }
        */


        try {
            String json = "{ \"mpeId\":\"" + mpe.getUPN().getId() + "\" }";
            String gout = HttpRequest.postGetString(Configuration.host()+"/tenderdirector/checkTenderToCancel2Tour", json);
            System.out.println("CHECK NEED FOR BACK OK" + gout);
        } catch (Exception e) {
            System.err.println("CHECK NEED FOR BACK ERROR");
            System.err.println(e.getMessage());
            e.printStackTrace();
        }


        try {
            ManagedProcessExecution mpeReloaded = ManagedProcessExecution.load(mpe.getUPN());

            String decision = mpeReloaded.getMetadataValueByKey("decision");
            System.out.println(decision);

            try {
                if (Objects.equals("cancel", decision)){
                    //mpeReloaded.setNextstage("Tour1_Step2");
                    System.out.println("___ ! BACK TO STEP 2");
                    mpe.setNextstage("Tour1_Step2");
                    System.out.println("___ ! BACK TO STEP 2 DONE");
                }
                else
                {
                    System.out.println("___ ! NORMAL NEXT STAGE");
                }

            } catch (ManagedProcessException.StageNotExists stageNotExists) {
                System.out.println("___ ! ERROR IN BACK TO STEP 2");
                stageNotExists.printStackTrace();
            }

        } catch (Exception e) {
            System.err.println("BACK STAGE ERROR");
            System.err.println(e.getMessage());
        }

    }

}
