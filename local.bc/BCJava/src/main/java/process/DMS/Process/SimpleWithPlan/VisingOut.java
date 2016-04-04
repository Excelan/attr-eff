package process.DMS.Process.SimpleWithPlan;

import digital.erp.process.ManagedProcessException;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageOut;

import java.util.Objects;

public class VisingOut implements StageOut {

    public void process(ManagedProcessExecution mpe)
    {
        String decision = mpe.getMetadataValueByKey("decision");
        System.out.println(decision);

        try {
            if (Objects.equals("cancel", decision)) mpe.setNextstage("Editing");
        } catch (ManagedProcessException.StageNotExists stageNotExists) {
            stageNotExists.printStackTrace();
        }
    }

}
