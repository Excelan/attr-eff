package process.DMS.Claims.Claim;

import digital.erp.process.ManagedProcessException;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageOut;

import java.util.Objects;

public class ReviewingOut implements StageOut {

    public void process(ManagedProcessExecution mpe)
    {
        String decision = mpe.getMetadataValueByKey("decision");
        System.out.println(decision);

        try {
            if (Objects.equals("cancel", decision)) mpe.setNextstage("Doing");
        } catch (ManagedProcessException.StageNotExists stageNotExists) {
            stageNotExists.printStackTrace();
        }
    }

}
