package odt_new;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by Iryna on 11.01.2016.
 */
public class MasterPlanInfo {
    public String initialdate="31/12/2015";
    public String lastdate="31/10/2015";
    public String period="1 год";
    public String policy="Согласно данной политике валидациия должна проводится раз в месяц.";

    public static class TableMasterRow {
        public String businessobject;
        public String programm;
        public String valdate;
    }
    public final List<TableMasterRow> tablerows = new ArrayList<>();
}
