package edu.rpi.pagr.service;

import android.app.Service;
import android.content.Intent;
import android.os.IBinder;
import android.widget.Toast;

import org.apache.http.HttpResponse;
import org.apache.http.HttpStatus;
import org.apache.http.NameValuePair;
import org.apache.http.StatusLine;
import org.apache.http.client.HttpClient;
import org.apache.http.client.entity.UrlEncodedFormEntity;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.message.BasicNameValuePair;
import org.apache.http.util.EntityUtils;

import java.util.ArrayList;

import edu.rpi.pagr.PagrDialogActivity;
import edu.rpi.pagr.utils.GatewayConnectionUtils;

/**
 * Created by Daniel Zhao on 10/20/13.
 */
public class NotificationService extends Service {

    private mThread mthread;
    public boolean isRunning = false;

    public IBinder onBind(Intent intent) {
        return null;
    }

    @Override
    public void onCreate() {
        super.onCreate();
        mthread = new mThread();

//        Toast.makeText(getBaseContext(), "start service", Toast.LENGTH_LONG).show();
    }

    @Override
    public synchronized void onDestroy() {
        super.onDestroy();
        if(!isRunning){
            mthread.interrupt();
//            mthread.stop();
        }
    }

    @Override
    public synchronized void onStart(Intent intent, int startId) {
        super.onStart(intent, startId);
        if(!isRunning){
            mthread.start();
            isRunning = true;
        }
    }

    public String getPagr(){
        try {

            HttpClient httpclient = new DefaultHttpClient();
            HttpPost httppost = new HttpPost(GatewayConnectionUtils.getApplicationBridgeBase() + GatewayConnectionUtils.getShouldPage());

            HttpResponse response = httpclient.execute(httppost);

            StatusLine status = response.getStatusLine();

            if (status.getStatusCode() == HttpStatus.SC_OK) {
                return new String(EntityUtils.toByteArray(response.getEntity()), "ISO-8859-1");
            }
        } catch (Exception ignored) {
        }
        return null;
    }

    public void ackPage(){
        try {

            HttpClient httpclient = new DefaultHttpClient();
            HttpPost httppost = new HttpPost(GatewayConnectionUtils.getApplicationBridgeBase() + GatewayConnectionUtils.getAckPage());

            HttpResponse response = httpclient.execute(httppost);

            StatusLine status = response.getStatusLine();

            if (status.getStatusCode() == HttpStatus.SC_OK) {
                return;
            }
        } catch (Exception ignored) {}
    }

    private void notifyUser() {
        Intent dialogIntent = new Intent(this, PagrDialogActivity.class);
        dialogIntent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
        this.startActivity(dialogIntent);
    }

    class mThread extends Thread{
        static final long DELAY = 6000;
        String shouldPage = null;

        @Override
        public void run(){
            while(isRunning){
                try {
                    shouldPage = getPagr();
                    Thread.sleep(DELAY);
                } catch (InterruptedException e) {
                    isRunning = false;
                    e.printStackTrace();
                }

                if (shouldPage != "1" ) {
                    ackPage();
                    notifyUser();
                    isRunning = false;
                    onDestroy();
                }
            }
        }

    }
}
