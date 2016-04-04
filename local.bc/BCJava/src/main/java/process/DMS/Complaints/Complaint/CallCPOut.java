package process.DMS.Complaints.Complaint;

import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageOut;

public class CallCPOut implements StageOut {

    public void process(ManagedProcessExecution mpe)
    {
        System.out.println("!!! < STAGE OUT CallCPOut < PROCESS");
    }

}
