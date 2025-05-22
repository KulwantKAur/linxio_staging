// doc: https://ftcloud.streamax.com:20002/DOC/Sign%20Authentication
// online java compiler: https://www.tutorialspoint.com/compile_java_online.php

import javax.crypto.Cipher;
import javax.crypto.spec.SecretKeySpec;
import java.nio.charset.StandardCharsets;
import java.security.MessageDigest;
import java.util.Base64;

public class SignApp {
   public static void main(String[] args) {
       try {
           Integer appId = 10001;
           Integer tenantId = 1;
           String tenantSecret = "secret";
           System.out.println(sign(appId,tenantId,tenantSecret));
       } catch (Exception e) {
           e.printStackTrace();
       }
   }

   private static String sign(Integer appId, Integer tenantId, String tenantSecret) throws Exception {
       long nowMillis = System.currentTimeMillis();
       String signJson = "{\"appId\":" + appId + ",\"signTime\":" + nowMillis + ",\"tenantId\":" + tenantId + ",\"tenantSecret\":\"" + tenantSecret + "\"}";
       // Generate signature
       String signString = Base64.getEncoder().encodeToString(signJson.getBytes(StandardCharsets.UTF_8));
       byte[] input = signString.getBytes(StandardCharsets.UTF_8);
       MessageDigest md = MessageDigest.getInstance("MD5");
       byte[] thedigest = md.digest(tenantSecret.getBytes(StandardCharsets.UTF_8));
       SecretKeySpec skc = new SecretKeySpec(thedigest, "AES");
       Cipher cipher = Cipher.getInstance("AES");
       cipher.init(Cipher.ENCRYPT_MODE, skc);
       byte[] cipherText = new byte[cipher.getOutputSize(input.length)];
       int ctLength = cipher.update(input, 0, input.length, cipherText, 0);
       cipher.doFinal(cipherText, ctLength);
       StringBuilder sb = new StringBuilder();
       for (byte b : cipherText) {
           String hex = Integer.toHexString(b & 0xFF);
           if (hex.length() == 1) {
               hex = '0' + hex;
           }
           sb.append(hex);
       }
       return sb.toString();
   }
}