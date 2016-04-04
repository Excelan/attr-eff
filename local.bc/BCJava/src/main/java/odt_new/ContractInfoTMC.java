package odt_new;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by Iryna on 06.01.2016.
 */
public class ContractInfoTMC {
    public String number="15";
    public String place="г.Киев";
    public String contdate="29.01.2015";
    public String introduction="Этот договор подписывается между ....";
    public String contractsubject ="Предмет договора...";
    public String orderofworksexecution ="Обязанностями исполнителя является...";
    public String rights="Права заказчика в этом договоре: ...";
    public String termsofpayment="Тридцать дней отсрочки платежа...";
    public String liabilities="Обе стороны обязуются...";
    public String changesofcntract="Договор может быть расторжен в случаи форс-мажора...";
    public String specialconditions="Особые условия этого договора...";
    public String termsofcontract="Этот договор действует один год...";

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

