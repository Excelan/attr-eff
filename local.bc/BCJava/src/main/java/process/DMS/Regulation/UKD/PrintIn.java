package process.DMS.Regulation.UKD;

import digital.erp.data.Entity;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageIn;
import digital.erp.symbol.Prototype;
import digital.erp.symbol.PrototypeException;
import digital.erp.symbol.URN;
import net.goldcut.network.HttpRequest;
import net.goldcut.utils.BarCodeGenerator;
import net.goldcut.utils.Configuration;

import javax.json.JsonObject;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;

public class PrintIn implements StageIn {

    private void generateCode128(Integer code)
    {
        System.out.println(code);
        BarCodeGenerator.generateCode128(code);
    }

    private void generateManagedCopyPDF()
    {

    }

    /**
     * count of plannedreceivers[]
     * generate Copy:Managed[] for plannedreceivers[]
     * generate code128[] for plannedreceivers[]
     * generate pdf with code128 - http gate call (copyid) for plannedreceivers[]
     * pack all pdf[] to archive
     * @param mpe
     */
    public void process(ManagedProcessExecution mpe)
    {

        try {
            Prototype resultPrototype = Prototype.fromString("Management:Post:Individual");
            URN fromURN = mpe.getSubject();
            List<URN> plannedreceivers = Entity.directLoadURNListForKeyIn("plannedreceivers", resultPrototype, fromURN);

            System.out.println("LOOK 2");
            System.out.println(plannedreceivers);

            //plannedreceivers.stream().map(urn -> urn.getId().intValue()).forEach(id -> generateCode128(id));

            String sopurnstring = mpe.getMetadataValueByKey("sop");


        } catch (PrototypeException.IncorrectFormat incorrectFormat) {
            incorrectFormat.printStackTrace();
        } catch (Exception e) {
            e.printStackTrace();
        }

    }

}
