package odt_new;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by Iryna on 06.01.2016.
 */
public class ContractInfoLOP {
    public String number="15";
    public String place="г.Киев";
    public String contdate="29.01.2015";
    public String introduction="Этот договор подписывается между ....";

    public String contractsubject="Предметом этого договора является ...";
    public String objectforrent="Объектом аренды";
    public String timeofrent="Время аренды 1 год";
    public String priceandterms="Цена ареды 20000";
    public String responsibilitiesoflandlord="Ответственность арендодателя";
    public String responsibilities="Ответственность этого ...";
    public String termsofreturn="Условия возврата ...";
    public String liabilities="Ответственность двух сторон ";
    public String disputesresolving="Все споры решаются в суде";
    public String forcemajeure="В случаи форс-мажора возможны...";
    public String contracttermination="Прекращение договора возможно в случаи не исполнения обязательств";
    public String otherconditions="Другие условия этого договора";
    public String appendix="Приложения к этому договору прилагаются к этому договору";


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

