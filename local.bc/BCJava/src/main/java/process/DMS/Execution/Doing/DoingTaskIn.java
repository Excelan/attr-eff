package process.DMS.Execution.Doing;

import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageIn;

public class DoingTaskIn  implements StageIn {

    public void process(ManagedProcessExecution mpe)
    {
        System.out.println("!!! > DoingTaskIn In");
    }

}