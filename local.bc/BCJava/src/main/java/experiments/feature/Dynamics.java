package experiments.feature;

/*
Дело в том, что Class.forName() приводит к загрузке класса и инициализации его статической части.
В свою очередь многие JDBC драйвера при статической инициализации регистрируют себя в DriverManager'е. Так что все дело в side effect'ах.

public Foo(String bar, int baz) {
}
Constructor c = Class.forName("Foo").getConstructor(String.class, Integer.TYPE);
Foo foo = (Foo) c.newInstance("example", 34);
*/
public class Dynamics {
}
