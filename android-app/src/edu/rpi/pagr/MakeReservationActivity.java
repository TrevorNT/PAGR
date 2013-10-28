package edu.rpi.pagr;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.TextView;

import com.actionbarsherlock.app.SherlockActivity;

/**
 * Created by Daniel Zhao on 9/25/13.
 */
public class MakeReservationActivity extends SherlockActivity {

    private String WAITING_TIME;
    private TextView waiting_time_text;
    private Button button_confirm_reservation;

    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_waiting_time);

        waiting_time_text = (TextView) findViewById(R.id.waiting_time_number);
        button_confirm_reservation = (Button) findViewById(R.id.button_yes);

        Bundle extras = getIntent().getExtras();
        if (extras != null) {
            String value = extras.getString(WAITING_TIME);
            waiting_time_text.setText( value );
        }
        button_confirm_reservation.setOnClickListener( new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(getBaseContext(), AppetizerListActivity.class);
                startActivity(intent);
                finish();
            }
        });
    }
}