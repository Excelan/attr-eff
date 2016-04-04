package process.DMS.Decisions.Reviewing;

import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageIn;

public class ReviewIn implements StageIn {

    public void process(ManagedProcessExecution mpe)
    {
        System.out.println("!!! > Review In");
    }

}