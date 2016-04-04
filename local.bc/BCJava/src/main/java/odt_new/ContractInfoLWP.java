package odt_new;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by Iryna on 06.01.2016.
 */
public class ContractInfoLWP {
    public String number="15";
    public String place="г.Киев";
    public String contdate="29.01.2015";
    public String introduction="Этот договор подписывается между ....";
    public String wordsdefinition="Склад - это...";
    public String subjectofcontract="Предметом - этого договора является";
    public String warehouseconditions="Условия склада ...";
    public String leabilities="Ответственность  склада";
    public String rights="Права арендодатора";
    public String lenlordleabilities="Ответственность ...";
    public String lenlordrights="Права арендодателя....";
    public String rentpayments="Платежи за склад ...";
    public String partyliabilities="Ответственность сторон";
    public String contractterm="Условиями этого договора является...";
    public String specialconditions="Особые условия....";
    public String finalpar="Тут должна быть важная информация....";


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

