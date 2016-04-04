package odt_new;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by Iryna on 06.01.2016.
 */
public class CapaInfo {

    public  String decriptiondeviation ="Палеты слишком близко к стене";
    List<String> risks;


    public static class TableCapaRow {
        public String descriptioncorrection;
        public String eventplace;
        public String controlresponsible;
        public String realizationdate;
        public String factdate;
        public String capastatus;
    }
    public final List<TableCapaRow> tablerows = new ArrayList<>();

}
