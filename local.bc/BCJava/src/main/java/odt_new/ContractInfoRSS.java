package odt_new;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by Iryna on 06.01.2016.
 */
public class ContractInfoRSS {
    public String number="15";
    public String place="г.Киев";
    public String contdate="29.01.2015";
    public String introduction="Этот договор подписывается между ....";

    public String wordsdefinition="Склад - это...";
    public String subjectofcontract="Предметом - этого договора является";
    public String responsibilityofdoer="Ответственность за ...";
    public String responsibility="Ответственность ...";
    public String priceandterm="Условия оплаты ...";
    public String insurance="Страхование должно проходить согласно";
    public String accounting="Расчеты ведутся";
    public String trademarks="Основные марки";
    public String confidentiality="Полная конфиденциальность";
    public String timeofcontract="Этот контракт действует";
    public String forcemajeure="Форс-мажорные обстоятельства";
    public String refuce="Тут должен быть пункт договора";
    public String fullcontract="ПОлный договор включает в себя...";
    public String language="Главный язык - этого договора русский";
    public String jurisdiction="Главной юрисдикцией считается Украина";
    public String otherconditions="Другие условия этого договора";


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

