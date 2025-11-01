package com.example.mysms

import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.os.Bundle
import android.telephony.SmsManager
import android.telephony.SmsMessage
import android.util.Log
import android.widget.Toast

class ReceiveSMS : BroadcastReceiver() {

    override fun onReceive(context: Context?, intent: Intent?) {
        if (intent?.action == "android.provider.Telephony.SMS_RECEIVED") {
            val bundle: Bundle? = intent.extras
            if (bundle != null) {
                val pdus = bundle["pdus"] as Array<*>
                val messages = Array(pdus.size) { i ->
                    SmsMessage.createFromPdu(pdus[i] as ByteArray)
                }

                for (message in messages) {
                    val msgBody = message.messageBody
                    val sender = message.originatingAddress

                    Log.d("ReceiveSMS", "Received SMS from $sender: $msgBody")
                    Toast.makeText(context, "SMS from $sender: $msgBody", Toast.LENGTH_SHORT).show()

                    if (msgBody.startsWith("Where")) {
                        sender?.let { sendSMS(it, "Position Report 21.03524 105.850182", context) }
                    }
                }
            }
        }
    }

    private fun sendSMS(number: String, message: String, context: Context?) {
        try {
            val smsManager = SmsManager.getDefault()
            smsManager.sendTextMessage(number, null, message, null, null)
            Log.d("ReceiveSMS", "Reply SMS sent to $number: $message")
            Toast.makeText(context, "Reply sent to $number", Toast.LENGTH_SHORT).show()
        } catch (e: Exception) {
            Log.e("ReceiveSMS", "Failed to send SMS to $number", e)
            Toast.makeText(context, "Failed to send SMS to $number", Toast.LENGTH_SHORT).show()
        }
    }
}
