package net.goldcut.utils;

public class DebugPrinter {
    public static void formatted(String msg, Object... args) {
        System.out.println(String.format(msg, args));
    }
}
