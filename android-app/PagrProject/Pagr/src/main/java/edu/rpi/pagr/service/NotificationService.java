package edu.rpi.pagr.service;

import android.app.Service;
import android.content.Intent;
import android.os.Bundle;
import android.os.IBinder;
import android.util.Log;
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
    private String mUUID;
    private String mReservationID;

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
        }
    }

    @Override
    public synchronized void onStart(Intent intent, int startId) {
        super.onStart(intent, startId);
        Bundle b = intent.getExtras();
        mUUID = b.getString("UUID");
        mReservationID = b.getString("reservationID");

//        Toast.makeText(getBaseContext(), mReservationID + "@@" + mUUID, Toast.LENGTH_SHORT).show();

        if(!isRunning){
            mthread.start();
            isRunning = true;
        }
    }

    public String getPagr(){
        try {
            ArrayList<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>();
            nameValuePairs.add(new BasicNameValuePair("handset_id", mUUID ) ); //mUUID
            nameValuePairs.add(new BasicNameValuePair("reservation_id", mReservationID ) );

            HttpClient httpclient = new DefaultHttpClient();
            HttpPost httppost = new HttpPost(GatewayConnectionUtils.getApplicationBridgeBase() + GatewayConnectionUtils.getShouldPage());

            httppost.setEntity(new UrlEncodedFormEntity(nameValuePairs));
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
        static final long DELAY = 2000;
        String shouldPage = null;

        @Override
        public void run(){ // 1 is notify user, 0 do nothing
            while(isRunning){
                try {
                    shouldPage = getPagr();
                    Log.v("Should Page Return Text", shouldPage);

                    Thread.sleep(DELAY);
                } catch (InterruptedException e) {
                    isRunning = false;
                    e.printStackTrace();
                }
//                Toast.makeText(getBaseContext(), shouldPage ,Toast.LENGTH_SHORT).show();

                if ( shouldPage.equalsIgnoreCase("1") ) {
                    ackPage();
                    notifyUser();
                    isRunning = false;
                    onDestroy();
                }
            }
        }

    }
}
