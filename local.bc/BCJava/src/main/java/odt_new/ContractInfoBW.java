package odt_new;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by Iryna on 06.01.2016.
 */
public class ContractInfoBW {

    public String number = "15";
    public String place = "г.Киев";
    public String contdate = "29.01.2015";
    public String timecontract = "NOT SET";
    public String introduction;
    public String contractsubject = "Предмет договора...";

    public String rightsandliabilities = "Права и обязанности сторон в этом догооворе";
    public String timeofworks = "сроки договора описаны с этом разделе";
    public String termofcustompayments = "Отсрочка платежа 30 дней";
    public String payments = "Платежи должны быть сделаны да 30-го числа...";
    public String specialconditions = "Особые условия этого догоовора";
    public String otherconditions = "Другие условия договора прописаны в этом пункте...";

    public String requisites = "реквизиты Биокон";
    public String requisitescont = "реквизиты контрагента";

    public String director = "Павлова В.В.";

    public static class ApplicationRow {
        public String text;

        public static class MediaRow {
            public String attachment;
            public String text;
        }

        public final List<MediaRow> mediarows = new ArrayList<>();

    }

    public final List<ApplicationRow> tablerows = new ArrayList<>();



}
