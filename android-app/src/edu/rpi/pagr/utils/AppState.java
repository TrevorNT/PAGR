package edu.rpi.pagr.utils;
/**
 * Created by danielzhao on 7/21/13.
 */
import android.content.Context;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.util.Log;

public class AppState {

    private static AppState instance = new AppState();
    private static Context context;
    private ConnectivityManager connectivityManager;
    private NetworkInfo networkInfo;
    private boolean connected = false;

    public static AppState getInstance(Context ctx) {
        context = ctx;
        return instance;
    }

    public boolean isOnline(Context con) {
        try {
            connectivityManager = (ConnectivityManager) con
                    .getSystemService(Context.CONNECTIVITY_SERVICE);

            networkInfo = connectivityManager.getActiveNetworkInfo();
            connected = networkInfo != null && networkInfo.isAvailable() &&
                    networkInfo.isConnected();
            return connected;

        } catch (Exception e) {
            Log.v("connectivity", e.toString());
        }
        return connected;
    }
}