package odt_new;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by Iryna on 06.01.2016.
 */
public class ContractInfoSS {
    public String number="15";
    public String place="г.Киев";
    public String contdate="29.01.2015";
    public String introduction="Этот договор подписывается между ....";
    public String contractsubject ="Предмет договора...";
    public String orderofworksexecution ="Обязанностями исполнителя является...";
    public String price="Права заказчика в этом договоре: ...";
    public String payments="Тридцать дней отсрочки платежа...";
    public String termofworks="Обе стороны обязуются...";
    public String maintanance="Договор может быть расторжен в случаи форс-мажора...";
    public String worksdoing="Особые условия этого договора...";
    public String guarantees="Этот договор действует один год...";
    public String executedworks="Работы должны быть выполнены в срок";
    public String partiesliabilities="Стороны обязуются ...";
    public String changes="В случаи изменений условий договора";
    public String timeofcontract="Этот контракт действует на протяжении года";
    public String forcemajeure="При форс-мажорных обстоятельствах";
    public String otherconditions="При изминение договора, договор нуждается в пересмотре.";
    public String appendix="Приложение 1, Приложение 2, блок схема";


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

