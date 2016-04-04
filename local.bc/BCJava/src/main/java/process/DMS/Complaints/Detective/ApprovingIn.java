package process.DMS.Complaints.Detective;

import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageIn;

public class ApprovingIn implements StageIn {

    public void process(ManagedProcessExecution mpe)
    {
        System.out.println("!!! > ApprovingIn");
    }

}

