package process.DMS.Complaints.Detective;

import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageOut;

public class RouteOut implements StageOut {

    public void process(ManagedProcessExecution mpe)
    {
        System.out.println("> OUT Detective.RouteOut");
    }

}

