package net.goldcut.utils;

import java.lang.reflect.Field;

public class MetaProgrammingUtils {

    public static boolean set(Object object, String fieldName, Object fieldValue) {
        Class<?> clazz = object.getClass();
        while (clazz != null) {
            try {
                Field field = clazz.getDeclaredField(fieldName);
                field.setAccessible(true);
                field.set(object, fieldValue);
                //System.out.println("**** OK");
                return true;
            } catch (NoSuchFieldException e) {
                //System.out.println("**** NoSuchFieldException");
                clazz = clazz.getSuperclass();
            } catch (Exception e) {
                //System.out.println("**** IllegalStateException");
                throw new IllegalStateException(e);
            }
        }
        //System.out.println("**** FALSE");
        return false;
    }

}
