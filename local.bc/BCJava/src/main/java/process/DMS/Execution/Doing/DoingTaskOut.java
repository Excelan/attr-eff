package process.DMS.Execution.Doing;

import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageOut;

public class DoingTaskOut implements StageOut {

    public void process(ManagedProcessExecution mpe)
    {
        System.out.println("< OUT DoingTaskOut");
        /*
        try {
            mpe.setNextstage("Editing");
        } catch (ManagedProcessException.StageNotExists stageNotExists) {
            stageNotExists.printStackTrace();
        }
        */
    }

}