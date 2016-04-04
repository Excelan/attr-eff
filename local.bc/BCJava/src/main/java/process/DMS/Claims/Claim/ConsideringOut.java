package process.DMS.Claims.Claim;

import digital.erp.process.ManagedProcessException;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageOut;

import java.util.Objects;

public class ConsideringOut implements StageOut {

    public void process(ManagedProcessExecution mpe)
    {
        // TODO
        String decision = mpe.getMetadataValueByKey("consideringdecision");
        System.out.println(decision);

        try {
            if (Objects.equals("cancel", decision)) mpe.setNextstage("Editing");
        } catch (ManagedProcessException.StageNotExists stageNotExists) {
            stageNotExists.printStackTrace();
        }
    }

}
