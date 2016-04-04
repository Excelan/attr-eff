package apptest;

import java.io.IOException;
import java.util.logging.*;

/**
 * Created by attracti on 2/25/16.
 */
public class Logging {
    private static Logger
            lgr = Logger.getLogger("prototype"),
            lgr2 = Logger.getLogger("class");

    static void printLogMessages(Logger logger) {
        logger.finest(logger.getName() + " Finest");
        logger.finer(logger.getName() + " Finer");
        logger.fine(logger.getName() + " Fine");
        logger.config(logger.getName() + " Config");
        logger.info(logger.getName() + " Info");
        logger.warning(logger.getName() + " Warning");
        logger.severe(logger.getName() + " Severe");
    }
    static void logMessages() {
        printLogMessages(lgr);
        printLogMessages(lgr2);

    }
    static void printLevels() {
        System.out.println(" -- printing levels -- "
                + lgr.getName() + " : " + lgr.getLevel()
                + " " + lgr2.getName() + " : " + lgr2.getLevel());
    }
    // we can use it like a filter
  //  LogRecord log = new LogRecord(Level.ALL, "here we log all Errors");


    public static void main(String[] args) {

        FileHandler logall;
        FileHandler error;
        try {
            logall = new FileHandler("/Users/attracti/Documents/log/alllog.txt");
            lgr.addHandler(logall);
            lgr.setLevel(Level.ALL);
            lgr2.addHandler(logall);
            lgr2.setLevel(Level.ALL);

            error = new FileHandler("/Users/attracti/Documents/log/error.txt");
            lgr.addHandler(error);
            lgr.setLevel(Level.SEVERE);
            lgr2.addHandler(error);
            lgr2.setLevel(Level.SEVERE);


            SimpleFormatter formatter = new SimpleFormatter();
            logall.setFormatter(formatter);

        } catch (IOException e) {
            e.printStackTrace();
        }

        lgr.setLevel(Level.SEVERE);
        lgr2.setLevel(Level.SEVERE);
        printLevels();
        System.out.println("com level: SEVERE");
        logMessages();

       lgr.log(Level.SEVERE, "Error!!!!!!!!");

        //     monitor.expect("LoggingLevelManipulation.out");
    }
}
