package process.DMS.Complaints.Detective;

import digital.erp.process.ManagedProcessException;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageOut;

import java.util.Objects;

public class VisingOut implements StageOut {

    public void process(ManagedProcessExecution mpe)
    {
        System.out.println("< OUT Detective.VisingOut");

        String decision = mpe.getMetadataValueByKey("decision");
        System.out.println(decision);

        try {
            if (Objects.equals("cancel", decision)) mpe.setNextstage("ProtocolEditing");
        } catch (ManagedProcessException.StageNotExists stageNotExists) {
            stageNotExists.printStackTrace();
        }
    }

}

