package process.DMS.Correction.CAPA;

import digital.erp.process.ManagedProcessException;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageOut;
import net.goldcut.network.HttpRequest;
import net.goldcut.utils.Configuration;

import java.util.Objects;

public class VisingOut implements StageOut {

    public void process(ManagedProcessExecution mpe)
    {
        String decision = mpe.getMetadataValueByKey("decision");
        System.out.println(decision);

        try {
            if (Objects.equals("cancel", decision)){

                mpe.setNextstage("Editing");

                System.out.println(mpe.toString());

                try {
                    String json = "{ \"mpeId\":\"" + mpe.getUPN().getId() + "\" }";
                    String gout = HttpRequest.postGetString(Configuration.host()+"/capadirector/setCapaDefault", json);
                    System.out.println("REMAP setCapaDefault OK " + gout);
                } catch (Exception e) {
                    System.err.println("REMAP setCapaDefault ERRROR");
                    System.err.println(e.getMessage());
                    e.printStackTrace();
                }

            }
        } catch (ManagedProcessException.StageNotExists stageNotExists) {
            stageNotExists.printStackTrace();
        }
    }

}
