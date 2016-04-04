package odt_new;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by Iryna on 06.01.2016.
 */
public class ContractInfoMT {
    public String number="15";
    public String place="г.Киев";
    public String contdate="29.01.2015";
    public String introduction="Этот договор подписывается между ....";
    public String contractsubject ="Предмет договора...";

    public String qualityofgoods="Качество товара, ...";
    public String deliveryconditions="Доставка должна быть...";
    public String goodstransfer="Перемещение товаров";
    public String termsofpayment="Условия платежа";
    public String termsofcontract="Условия договора";
    public String liabilities="Обязательства";
    public String finalpar="Финальные положения в этом договоре";

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

