package process.DMS.Complaints.Detective;

import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageOut;

public class ProtocolExtendRiskOut implements StageOut {

    public void process(ManagedProcessExecution mpe)
    {
        System.out.println("< OUT Detective.ProtocolExtendRiskOut");
        /*
        try {
            mpe.setNextstage("Editing");
        } catch (ManagedProcessException.StageNotExists stageNotExists) {
            stageNotExists.printStackTrace();
        }
        */
    }

}
