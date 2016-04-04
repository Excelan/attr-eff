package odt_new;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by Iryna on 06.01.2016.
 */
public class ContractInfoLC {
    public String number="15";
    public String place="г.Киев";
    public String contdate="29.01.2015";
    public String introduction="Этот договор подписывается между ....";
    public String contractsubject ="Предмет договора...";

    public String payments="Платежи с отсрочкой 30 дней";
    public String leabilities="Обязанности сторон";
    public String disputeresolutions="В случаи возникновения споров...";
    public String finalpar="Важные условия могут быть в этом пункте";


    public String requisites="реквизиты Биокон";
    public String requisitescont="реквизиты контрагента";

    public String director="Павлова В.В.";

    public static class ApplicationRow {
        public String text;
        public static class MediaRow{
            public String urii;
            public String picturename;
        }
        public final List <MediaRow> mediarows=new ArrayList<>();

    }
    public final List<ApplicationRow> tablerows = new ArrayList<>();
}

