package edu.rpi.pagr;

import android.content.Context;
import android.content.Intent;
import android.os.AsyncTask;
import android.os.Bundle;
import android.telephony.TelephonyManager;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.TextView;
import android.widget.Toast;

import com.actionbarsherlock.app.SherlockActivity;

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
import java.util.UUID;

import edu.rpi.pagr.service.NotificationService;
import edu.rpi.pagr.utils.GatewayConnectionUtils;

/**
 * Created by Daniel Zhao on 9/25/13.
 */
public class MakeReservationActivity extends SherlockActivity {

    private String mPartySize;
    private String mGuestName;

    private TextView waiting_time_text;
    private Button button_confirm_reservation;
    private AsyncTask<Void, Void, String> mCreateReservationTask;

    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_waiting_time);

        waiting_time_text = (TextView) findViewById(R.id.waiting_time_number);
        button_confirm_reservation = (Button) findViewById(R.id.button_yes);

        Intent intent = getIntent();

        String waitingTime = (String) intent.getSerializableExtra("WAITING_TIME");
        waiting_time_text.setText( waitingTime );

        mPartySize = (String) intent.getSerializableExtra("PARTY_SIZE");
        mGuestName = (String) intent.getSerializableExtra("GUEST_NAME");

        button_confirm_reservation.setOnClickListener( new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                mCreateReservationTask = new CreateReservationTask().execute();
            }
        });
    }

    private class CreateReservationTask extends AsyncTask<Void, Void, String> {

        @Override
        protected String doInBackground(Void... params) {
            try {
                if ( isCancelled() )
                    return null;

                // Send Values
                ArrayList<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>();
                nameValuePairs.add(new BasicNameValuePair("handset_id", getUserID() ) ); //getUserID()
                nameValuePairs.add(new BasicNameValuePair("party_size", mPartySize ) );
                if (mGuestName == null) {
                    mGuestName = "Guest";
                }
                nameValuePairs.add(new BasicNameValuePair("patron_name", mGuestName ) );

                HttpClient httpclient = new DefaultHttpClient();
                HttpPost httppost = new HttpPost(GatewayConnectionUtils.getApplicationBridgeBase()+GatewayConnectionUtils.getCreateReservation());

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

        @Override
        protected void onPostExecute(String result) {

            mCreateReservationTask = null;

            if (result != null) {
                try {
                    int reservationID = Integer.parseInt( result );

                    Intent serviceIntent = new Intent(getBaseContext(), NotificationService.class);
                    Bundle b = new Bundle();
                    b.putString("reservationID", result);
                    b.putString("UUID", getUserID());
                    serviceIntent.putExtras(b);

                    startService(serviceIntent);

                    Intent intent = new Intent(getBaseContext(), ViewAppetizerActivity.class);
                    intent.putExtra("RESERVATION_ID", result);
                    intent.putExtra("UUID", getUserID());
                    startActivity(intent);
                    finish();
                } catch (Exception ignored) {
                    Toast.makeText( getBaseContext(), result, Toast.LENGTH_LONG).show();
                    Log.v("Make REservation Error", result);
                }
            }
        }
    }
    // Get UUID
    private String getUserID() {
        // Get Device UUID
        final TelephonyManager tm = (TelephonyManager) this.getBaseContext().getSystemService(Context.TELEPHONY_SERVICE);
        final String tmDevice, tmSerial, androidId;
        tmDevice = "" + tm.getDeviceId();
        tmSerial = "" + tm.getSimSerialNumber();
        androidId = "" + android.provider.Settings.Secure.getString(this.getContentResolver(), android.provider.Settings.Secure.ANDROID_ID);

        UUID deviceUuid = new UUID(androidId.hashCode(), ((long) tmDevice.hashCode() << 32) | tmSerial.hashCode());
        return deviceUuid.toString();
    }
}