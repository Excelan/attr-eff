package process.DMS.Complaints.Detective;

import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageIn;

public class VisingIn implements StageIn {

    public void process(ManagedProcessExecution mpe)
    {
        System.out.println("!!! > VisingIn");
    }

}