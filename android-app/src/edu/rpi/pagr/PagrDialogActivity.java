package edu.rpi.pagr;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.os.Vibrator;

import com.actionbarsherlock.app.SherlockFragmentActivity;

import edu.rpi.pagr.fragment.PagrDialogFragment;
import edu.rpi.pagr.service.NotificationService;

public class PagrDialogActivity extends SherlockFragmentActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        PagrDialogFragment pagrDialogFragment = new PagrDialogFragment();
        pagrDialogFragment.show(getSupportFragmentManager(), "PagrDialogFragment");
        Vibrator v = (Vibrator) getSystemService(Context.VIBRATOR_SERVICE);
        // Vibrate for 2000 milliseconds
//        v.vibrate(1000);

        stopService(new Intent(this, NotificationService.class));
    }


//    @Override
//    public boolean onCreateOptionsMenu(Menu menu) {
//        // Inflate the menu; this adds items to the action bar if it is present.
//        getMenuInflater().inflate(R.menu.settings, menu);
//        return true;
//    }
    
}
