package process.DMS.Complaints.Complaint;

import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageIn;

public class CallCPIn implements StageIn {

    public void process(ManagedProcessExecution mpe)
    {
        System.out.println("!!! > STAGE IN CallCPIn > PROCESS");
    }

}
