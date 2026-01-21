OPERATORS	UPLINK		     DOWNLINK

SFR. 		2500 - 2515 (15Mhz). 2620 - 2635 (15)

Orange. 	2515 - 2535 (20Mhz). 2635 - 2655 (20)

Bouyg. 		2535 - 2550 (15Mhz). 2655 - 2670 (15)

Free. 		2550 - 2570 (20Mhz). 2670 - 2690 (20)





###### LTE Maximum Downlink Throughput

In case of 5 MHz channel bandwidth, 300 subcarriers are used.



For a perfect idle condition 64 QAM can be used. That means each symbol is now allowed to carry 6 bits .



So the total bits carried by 300 subcarriers for the duration of a symbol is 300 X 6 = 1800 bits .



Again 1 symbol is of 71.4 microseconds for LTE. So the data rate is 1800 / 71.4 = 25.2 Mbps .



So the formula for calculating maximum data rate at physical layer is:



(Number of subcarriers X 6) / 71.4 microseconds



For 10 MHz using the same formula the maximum data rate in downlink is 50.4 Mbps and for 20 MHz it is 100.8 Mbps.



Note: With 4x4 MIMO, the peak data rate goes up to 100.8 Mbps x 4 = 403 Mbps.





###### LTE Useful Downlink Throughput

1\. Path Loss Calculation

&nbsp;	Using the Free Space Propagation Model, we calculate the signal loss over the distance between the antenna and the User Equipment (UE):

&nbsp;	Loss (dB) = 32.45 + 20log10(Frequency) + 20log10(Distance or Altitude)

&nbsp;	Loss = 32.45 + 20log10(2600) + 20log10(0.3)
	Loss = 32.45 + 68.30 - 10.46
	Total Path Loss: 90.29dB



2\. Received Power and SINR Calculation
	To find the Signal-to-Interference-plus-Noise Ratio (SINR), we first determine the received power P at the UE:
	Received Power P: 
		P = Transmission Power - Path Loss 
		P = 43dBm - 90.29dB => -47.29dBm
	SINR:
		SINR = P - (Noise + Interference)
		SINR = -47.29dBm - (-100) => 52.71dB



3\. CQI Mapping and Efficiency

&nbsp;	Based on the provided CQI table, an SINR of 52.71dB exceeds the maximum threshold listed 20.31dB. Therefore, we use CQI Index 15



4\. Useful Throughput Calculation

&nbsp;	For a 10MHz bandwidth with Extended Cyclic Prefix (CP), the resource grid is defined as follows:

&nbsp;		Resource Blocks (RBs): 50
		Subcarriers per RB: 12
		Symbols per Subframe (Extended CP): 12 symbols (6 per slot \* 2 slots)
		Total Resource Elements (REs) per Subframe: $50 \* 12 \* 12 = 7,200 REs
		Total REs per Second: 7,200 \* 1,000 subframes per sec => 7,200,000 REs/sec



5.Final Throughput Formula:
	Throughput = Total REs/sec \* Bits per symbol
	Throughput = 7,200,000 \* 5.5547
	Throughput = 39,993,840 bps ~ 40 Mbps

