package process.DMS.Decisions.Plan;

import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageOut;

public class PlanningOut implements StageOut {

    public void process(ManagedProcessExecution mpe)
    {
        System.out.println("< OUT Planning");
        /*
        try {
            mpe.setNextstage("Editing");
        } catch (ManagedProcessException.StageNotExists stageNotExists) {
            stageNotExists.printStackTrace();
        }
        */
    }

}